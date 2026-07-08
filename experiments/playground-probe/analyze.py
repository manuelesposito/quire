import zlib, struct, sys

def decode_png(path):
    data = open(path,'rb').read()
    assert data[:8] == b'\x89PNG\r\n\x1a\n'
    pos, idat, w, h, bpp = 8, b'', 0, 0, 0
    while pos < len(data):
        ln = struct.unpack('>I', data[pos:pos+4])[0]
        typ = data[pos+4:pos+8]
        chunk = data[pos+8:pos+8+ln]
        if typ == b'IHDR':
            w,h,bitd,color = struct.unpack('>IIBB', chunk[:10])
            bpp = {2:3, 6:4}[color]
        elif typ == b'IDAT': idat += chunk
        elif typ == b'IEND': break
        pos += 12+ln
    raw = zlib.decompress(idat)
    stride = w*bpp
    out = bytearray(w*h*bpp)
    prev = bytearray(stride)
    p = 0
    for y in range(h):
        f = raw[p]; p += 1
        line = bytearray(raw[p:p+stride]); p += stride
        if f == 1:
            for i in range(bpp, stride): line[i] = (line[i]+line[i-bpp])&255
        elif f == 2:
            for i in range(stride): line[i] = (line[i]+prev[i])&255
        elif f == 3:
            for i in range(stride):
                a = line[i-bpp] if i>=bpp else 0
                line[i] = (line[i]+((a+prev[i])>>1))&255
        elif f == 4:
            for i in range(stride):
                a = line[i-bpp] if i>=bpp else 0
                b = prev[i]
                c = prev[i-bpp] if i>=bpp else 0
                pa, pb, pc = abs(b-c), abs(a-c), abs(a+b-2*c)
                pr = a if (pa<=pb and pa<=pc) else (b if pb<=pc else c)
                line[i] = (line[i]+pr)&255
        out[y*stride:(y+1)*stride] = line
        prev = line
    return w,h,bpp,out

def region_avg(img, x0,y0,x1,y1):
    w,h,bpp,px = img
    rs=gs=bs=n=0
    for y in range(y0,min(y1,h),4):
        for x in range(x0,min(x1,w),4):
            o=(y*w+x)*bpp
            rs+=px[o]; gs+=px[o+1]; bs+=px[o+2]; n+=1
    return '#%02x%02x%02x'%(rs//n, gs//n, bs//n)

def diff_pct(a, b):
    wa,ha,ba,pa = a; wb,hb,bb,pb = b
    if (wa,ha)!=(wb,hb): return -1
    n=d=0
    for y in range(0,ha,6):
        for x in range(0,wa,6):
            oa=(y*wa+x)*ba; ob=(y*wb+x)*bb
            if abs(pa[oa]-pb[ob])+abs(pa[oa+1]-pb[ob+1])+abs(pa[oa+2]-pb[ob+2]) > 24: d+=1
            n+=1
    return 100.0*d/n

screens = sys.argv[1:] or ['dash','posts','settings']
for s in screens:
    imgs = {q: decode_png(f'{s}-q{q}.png') for q in (0,1,2)}
    print(f'== {s} ==')
    print(f'  q0->q1 changed pixels: {diff_pct(imgs[0],imgs[1]):.1f}%')
    print(f'  q0->q2 changed pixels: {diff_pct(imgs[0],imgs[2]):.1f}%')
    for q in (0,2):
        menu = region_avg(imgs[q], 4,300,150,700)
        body = region_avg(imgs[q], 900,700,1400,950)
        bar  = region_avg(imgs[q], 400,2,1000,26)
        print(f'  q{q}: adminmenu~{menu}  body~{body}  adminbar~{bar}')
